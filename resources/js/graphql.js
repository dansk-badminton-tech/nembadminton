import {createApolloProvider} from '@vue/apollo-option'
import {getAuthToken, isLoggedIn} from "./auth";

import {ApolloClient, HttpLink, ApolloLink, InMemoryCache} from '@apollo/client/core';

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

const ApolloClientInstance = new ApolloClient(
    {
        link: httpLinkWithAuth,
        cache: new InMemoryCache()
    });

const apolloProvider = createApolloProvider({
                                         defaultClient: ApolloClientInstance,
                                     }
)
export default apolloProvider
export {ApolloClientInstance}
