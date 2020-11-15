import ApolloClient from 'apollo-boost'
import VueApollo from 'vue-apollo'

const apolloClient = new ApolloClient(
    {
        uri: '/graphql',
        request: (operation) => {
            const token = localStorage.getItem('access_token')
            if (!!token) {
                operation.setContext(
                    {
                        headers: {
                            Authorization: token
                                           ? `Bearer ${token}`
                                           : ''
                        }
                    }
                )
            }
        }
    }
)

const apolloProvider = new VueApollo({
                                         defaultClient: apolloClient,
                                     }
)
export default apolloProvider
