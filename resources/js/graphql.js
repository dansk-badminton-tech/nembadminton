import VueApollo from 'vue-apollo'
import {getAuthToken, isLoggedIn} from "./auth";

import {ApolloClient, HttpLink, ApolloLink, InMemoryCache} from '@apollo/client/core';
import Pusher from 'pusher-js';
import PusherLink from "./pusher-link";

window.Pusher = Pusher;

const httpLink = new HttpLink({
                                  uri: '/graphql'
                              });

const authMiddleware = new ApolloLink((operation, forward) => {
    if (isLoggedIn()) {
        const token = getAuthToken();
        operation.setContext(({headers = {}}) => ({
            headers: {
                ...headers,
                authorization: token
                               ? `Bearer ${token}`
                               : '',
            }
        }));
    }

    return forward(operation);
});

const httpLinkWithAuth = authMiddleware.concat(httpLink);
const pusherLink = new PusherLink({
                                      pusher: new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
                                          cluster: '',
                                          wsHost: import.meta.env.VITE_PUSHER_HOST,
                                          wsPort: import.meta.env.VITE_PUSHER_PORT,
                                          forceTLS: import.meta.env.VITE_PUSHER_SCHEME === 'https',
                                          disableStats: true,
                                          enabledTransports: ['ws', 'wss'],
                                          channelAuthorization: {
                                              endpoint: `/graphql/subscriptions/auth`,
                                              headersProvider: () => {
                                                  return {
                                                      authorization: "Bearer "+getAuthToken(),
                                                  }
                                              },
                                          },
                                      }),
                                  });

const ApolloClientInstance = new ApolloClient(
    {
        link: ApolloLink.from([pusherLink, httpLinkWithAuth]),
        cache: new InMemoryCache()
    });

const apolloProvider = new VueApollo({
                                         defaultClient: ApolloClientInstance,
                                     }
)
export default apolloProvider
export {ApolloClientInstance}
